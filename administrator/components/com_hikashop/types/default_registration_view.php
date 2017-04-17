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
class hikashopDefault_registration_viewType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'login', JText::_('HIKA_LOGIN') );
		$this->values[] = JHTML::_('select.option', 0, JText::_('HIKA_REGISTRATION'));
		$this->values[] = JHTML::_('select.option', 1, JText::_('SIMPLIFIED_REGISTRATION'));
		$this->values[] = JHTML::_('select.option', 3, JText::_('SIMPLIFIED_REGISTRATION_WITH_PASSWORD'));
		$this->values[] = JHTML::_('select.option', 2, JText::_('GUEST'));
	}
	function display($map,$value){
		$this->load();
		$js="function changeDefaultRegistrationViewType(){
				var default_registration_view = window.document.getElementById('configdefault_registration_view');
				if(!default_registration_view) default_registration_view = window.document.getElementById('config[default_registration_view]');
				var display_login = window.document.getElementById('config_display_login1');
				if(!display_login) display_login = window.document.getElementById('config[display_login]1');
				var display_login_selection=display_login.checked;
				var normal = window.document.getElementById('config[simplified_registration][normal]');
				var simple = window.document.getElementById('config[simplified_registration][simple]');
				var simple_pwd = window.document.getElementById('config[simplified_registration][simple_pwd]');
				var guest = window.document.getElementById('config[simplified_registration][guest]');

				if(display_login_selection==true){
					addValue(default_registration_view,'login','".JText::_('HIKA_LOGIN',true)."');
				}else{
					removeByValue(default_registration_view, 'login');
				}
				if(normal.checked==true){
					addValue(default_registration_view,'0','".JText::_('HIKA_REGISTRATION',true)."');
				}else{
					removeByValue(default_registration_view, '0');
				}
				if(simple.checked==true){
					addValue(default_registration_view,'1','".JText::_('SIMPLIFIED_REGISTRATION',true)."');
				}else{
					removeByValue(default_registration_view, '1');
				}
				if(simple_pwd.checked==true){
					addValue(default_registration_view,'3','".JText::_('SIMPLIFIED_REGISTRATION_WITH_PASSWORD',true)."');
				}else{
					removeByValue(default_registration_view, '3');
				}
				if(guest.checked==true){
					addValue(default_registration_view,'2','".JText::_('GUEST',true)."');
				}else{
					removeByValue(default_registration_view, '2');
				}
			}
			function addValue(select, value,text) {
				if(existByValue(select, value)) return;
				var newListItem = document.createElement('OPTION');
				newListItem.text = text;
				newListItem.value = value;
				select.add(newListItem);
			}
			function removeByValue(select, value) {
				if(!existByValue(select, value)) return;
				for (var i=0, length = select.options.length; i< length; i++) {
					if (select.options[i] && select.options[i].value === value) {
						select.options[i] = null;
					}
				}
			}
			function existByValue(select, value) {
				for (var i=0, length = select.options.length; i< length; i++) {
					if (select.options[i] && select.options[i].value === value) {
						return true;
					}
				}
				return false;
			}
			window.hikashop.ready( function(){ window.hikashop.noChzn(); changeDefaultRegistrationViewType(); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox chzn-done no-chzn" size="1"', 'value', 'text', $value );
	}
}
