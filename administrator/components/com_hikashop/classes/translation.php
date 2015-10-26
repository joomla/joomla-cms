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
class hikashopTranslationClass extends hikashopClass{
	var $tables = array('jf_content');
	var $pkeys = array('id');
	var $namekeys = array('');
	var $toggle = array('published'=>'id');


	function getTable(){
		$trans_table = 'jf_content';
		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->isMulti();
		if($translationHelper->falang){
			$trans_table = 'falang_content';
		}
		return hikashop_table($trans_table,false);
	}
}
