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
class hikashopDisplay_methodType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 0, JText::_('ALL_IN_ONE_PAGE') );
		$this->values[] = JHTML::_('select.option', 1, JText::_('SWITCHER'));
	}
	function display($map,$value){
		$this->load();
		$js="function changeRegistrationButtonType(init){
				var display=window.document.getElementById('config[display_method]0').checked;
				var normal = window.document.getElementById('config[simplified_registration][normal]');
				var simple = window.document.getElementById('config[simplified_registration][simple]');
				var simple_pwd = window.document.getElementById('config[simplified_registration][simple_pwd]');
				var guest = window.document.getElementById('config[simplified_registration][guest]');
				var default_registration_view_tr = window.document.getElementById('default_registration_view_tr');

				if(display==true){
					normal.type='radio';
					simple.type='radio';
					simple_pwd.type='radio';
					guest.type='radio';
					default_registration_view_tr.style.display = 'none';
				}else{
					normal.type='checkbox';
					simple.type='checkbox';
					simple_pwd.type='checkbox';
					guest.type='checkbox';
					default_registration_view_tr.style.display = 'table-row';
				}
				if(!init){
					normal.checked=false; normal.parentNode.className = ''; normal.disabled=false;
					simple.checked=false; simple.parentNode.className = ''; simple.disabled=false;
					simple_pwd.checked=false; simple_pwd.parentNode.className = ''; simple_pwd.disabled=false;
					guest.checked=false;
					changeDefaultRegistrationViewType();
				}
			}
			window.hikashop.ready( function(){ changeRegistrationButtonType(true); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		return JHTML::_('select.radiolist',   $this->values, $map, 'class="inputbox" size="1" onChange="changeRegistrationButtonType(false);"', 'value', 'text', (int)$value );
	}
}
