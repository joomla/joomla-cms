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
class hikashopEncodingType{
	function hikashopEncodingType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'binary', 'Binary' );
		$this->values[] = JHTML::_('select.option', 'quoted-printable', 'Quoted-printable' );
		$this->values[] = JHTML::_('select.option', '7bit', '7 Bit');
		$this->values[] = JHTML::_('select.option', '8bit', '8 Bit' );
		$this->values[] = JHTML::_('select.option', 'base64', 'Base 64' );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1"', 'value', 'text', $value);
	}
}
