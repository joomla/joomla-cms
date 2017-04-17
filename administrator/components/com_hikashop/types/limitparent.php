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
class hikashopLimitparentType{
	function load($type,$object){
		$this->values = array();
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__hikashop_field WHERE field_table='.$db->Quote($type));
		$fields = $db->loadObjectList();
		if(!empty($fields)){
			foreach($fields as $field){
				if(is_object($object) && isset($object->field_namekey) && $object->field_namekey == $field->field_namekey) continue;
				$this->values[] = JHTML::_('select.option', $field->field_namekey,$field->field_realname);
			}
			if(count($this->values)){
				$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_ALL'));
			}
		}
	}
	function display($map,$value,$type,$parent_value,$object=null){
		$this->load($type,$object);
		if(!count($this->values)){
			return JText::_('AT_LEAST_ONE_FIELD_PUBLISHED');
		}
		if(is_array($parent_value)){
			$parent_value=implode(',', $parent_value);
		}
		$url=hikashop_completeLink('field&task=parentfield&type='.$type.'&value='.$parent_value,true,true);
		$js ="
		function hikashopLoadParent(namekey){
			try{
				new Ajax('".$url."&namekey='+namekey, { method: 'get', onComplete: function(result) { old = window.document.getElementById('parent_value'); if(old){ old.innerHTML = result;}}}).request();
			}catch(err){
				new Request({url:'".$url."&namekey='+namekey, method: 'get', onComplete: function(result) { old = window.document.getElementById('parent_value'); if(old){ old.innerHTML = result;}}}).send();
			}
		}
		window.hikashop.ready(function(){
			hikashopLoadParent(document.getElementById('limit_parent_select').value);
		});
		";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onChange="hikashopLoadParent(this.value);"', 'value', 'text', $value, 'limit_parent_select' );
	}
}
