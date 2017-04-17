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
class hikashopClickClass extends hikashopClass{
	var $tables = array('click');
	var $pkeys = array('click_id');

	function save(&$element){
		if(empty($element->click_id)){
			if(empty($element->click_created)){
				$element->click_created = time();
			}
			if(empty($element->click_ip)){
				$element->click_ip = hikashop_getIP();
			}

			if(empty($element->click_referer)){
				if(!empty($_SERVER['HTTP_REFERER']) && preg_match('#^https?://.*#i',$_SERVER['HTTP_REFERER'])){
					$element->click_referer=str_replace(array('"', '<', '>', "'"), '', @$_SERVER['HTTP_REFERER']);
				}
			}
		}
		return parent::save($element);
	}

	function getLatest($partner_id,$ip,$click_min_delay){
		$query = 'SELECT click_id FROM '.hikashop_table('click').' WHERE click_partner_id='.(int)$partner_id.' AND click_ip='.$this->database->Quote($ip).' AND click_created > '.(time()-$click_min_delay);
		$this->database->setQuery($query);
		return $this->database->loadResult();
	}
}
