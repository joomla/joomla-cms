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
class JElementSelectoptions extends JElement{
	function fetchElement($name, $value, &$node, $control_name){
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			return 'This menu options cannot be displayed without the Hikashop Component';
		}

		$config =& hikashop_config();
		if(!hikashop_isAllowed($config->get('acl_menus_manage','all'))){
			return 'Access to the HikaShop options of the menus is restricted';
		}

		$id = reset(JRequest::getVar( 'cid', array(), '', 'array' ));

		if(!empty($id)){
			$text = '<a title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=menus&task=edit&cid[]='.$id).'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
			$config =& hikashop_config();
			$hikashop_params = $config->get('menu_'.$id,null);
			if(empty($hikashop_params)){
				$text .= '<br/>'.JText::_('HIKASHOP_SAVE_OPTIONS_ONCE');
			}
		}else{
			$text = JText::_('HIKASHOP_OPTIONS_EDIT').'<br/>'.JText::_('HIKASHOP_SAVE_OPTIONS_ONCE');
		}
		return $text;
	}
}
