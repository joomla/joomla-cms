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
class TaxController extends hikashopController{
	var $type='tax';
	var $namekey = 'tax_namekey';
	var $table = 'tax';

	function cancel(){
		$return = JRequest::getString('return');
		if(!empty($return)){
			if(strpos($return,HIKASHOP_LIVE)===false && preg_match('#^https?://.*#',$return)) return false;
			$this->setRedirect(hikashop_completeLink(urldecode($return),false,true));
		}else{
			return $this->listing();
		}
		return true;
	}
}
